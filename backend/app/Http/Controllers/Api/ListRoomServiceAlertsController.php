<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class ListRoomServiceAlertsController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(Request $request): JsonResponse
    {
        $siteId = $this->resolveBranchSiteId($request);

        $rows = DB::table('room_time_services')
            ->leftJoin('companions', 'companions.id', '=', 'room_time_services.companion_id')
            ->where('room_time_services.status', 'open')
            ->whereNotNull('room_time_services.alert_at')
            ->whereNull('room_time_services.alert_notified_at')
            ->where('room_time_services.alert_at', '<=', now())
            ->when($siteId, fn ($q) => $q->where('room_time_services.site_id', (int) $siteId))
            ->orderBy('room_time_services.alert_at')
            ->limit(50)
            ->get([
                'room_time_services.id',
                'room_time_services.room_label',
                'room_time_services.alert_at',
                'room_time_services.planned_minutes',
                'companions.stage_name as companion_name',
            ])
            ->map(static fn ($r): array => [
                'service_id' => (int) $r->id,
                'room_label' => $r->room_label,
                'companion_name' => $r->companion_name,
                'planned_minutes' => $r->planned_minutes !== null ? (int) $r->planned_minutes : null,
                'alert_at' => $r->alert_at,
            ])
            ->values()
            ->all();

        return response()->json(['data' => $rows]);
    }
}
