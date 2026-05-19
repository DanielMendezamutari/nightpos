<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class ListRoomTimeServicesController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(Request $request): JsonResponse
    {
        $siteId = $this->resolveBranchSiteId($request);

        $rows = DB::table('room_time_services')
            ->leftJoin('companions', 'companions.id', '=', 'room_time_services.companion_id')
            ->leftJoin('room_time_service_payments', 'room_time_service_payments.room_time_service_id', '=', 'room_time_services.id')
            ->whereIn('room_time_services.status', ['open', 'closed', 'paid'])
            ->when($siteId, fn ($q) => $q->where('room_time_services.site_id', (int) $siteId))
            ->orderByDesc('room_time_services.id')
            ->limit(150)
            ->groupBy([
                'room_time_services.id',
                'room_time_services.site_id',
                'room_time_services.shift_turn_id',
                'room_time_services.waiter_user_id',
                'room_time_services.companion_id',
                'room_time_services.customer_name',
                'room_time_services.room_label',
                'room_time_services.rate_per_hour',
                'room_time_services.planned_minutes',
                'room_time_services.alert_before_minutes',
                'room_time_services.grace_minutes',
                'room_time_services.started_at',
                'room_time_services.alert_at',
                'room_time_services.alert_notified_at',
                'room_time_services.closed_at',
                'room_time_services.manual_minutes',
                'room_time_services.billed_minutes',
                'room_time_services.subtotal',
                'room_time_services.status',
                'room_time_services.notes',
                'companions.stage_name',
            ])
            ->get([
                'room_time_services.id',
                'room_time_services.site_id',
                'room_time_services.shift_turn_id',
                'room_time_services.waiter_user_id',
                'room_time_services.companion_id',
                'room_time_services.customer_name',
                'room_time_services.room_label',
                'room_time_services.rate_per_hour',
                'room_time_services.planned_minutes',
                'room_time_services.alert_before_minutes',
                'room_time_services.grace_minutes',
                'room_time_services.started_at',
                'room_time_services.alert_at',
                'room_time_services.alert_notified_at',
                'room_time_services.closed_at',
                'room_time_services.manual_minutes',
                'room_time_services.billed_minutes',
                'room_time_services.subtotal',
                'room_time_services.status',
                'room_time_services.notes',
                'companions.stage_name as companion_name',
                DB::raw('COALESCE(SUM(room_time_service_payments.amount),0) as paid_total'),
            ])
            ->map(static function ($r): array {
                $subtotal = (int) $r->subtotal;
                $paidTotal = (int) $r->paid_total;
                return [
                    'id' => (int) $r->id,
                    'site_id' => (int) $r->site_id,
                    'shift_turn_id' => (int) $r->shift_turn_id,
                    'companion_id' => $r->companion_id !== null ? (int) $r->companion_id : null,
                    'companion_name' => $r->companion_name,
                    'customer_name' => $r->customer_name,
                    'room_label' => $r->room_label,
                    'rate_per_hour' => (int) $r->rate_per_hour,
                    'planned_minutes' => $r->planned_minutes !== null ? (int) $r->planned_minutes : null,
                    'alert_before_minutes' => (int) $r->alert_before_minutes,
                    'grace_minutes' => (int) $r->grace_minutes,
                    'started_at' => $r->started_at,
                    'alert_at' => $r->alert_at,
                    'alert_notified_at' => $r->alert_notified_at,
                    'closed_at' => $r->closed_at,
                    'manual_minutes' => $r->manual_minutes !== null ? (int) $r->manual_minutes : null,
                    'billed_minutes' => (int) $r->billed_minutes,
                    'subtotal' => $subtotal,
                    'paid_total' => $paidTotal,
                    'balance_due' => max(0, $subtotal - $paidTotal),
                    'status' => $r->status,
                    'notes' => $r->notes,
                ];
            })
            ->values()
            ->all();

        return response()->json(['data' => $rows]);
    }
}

