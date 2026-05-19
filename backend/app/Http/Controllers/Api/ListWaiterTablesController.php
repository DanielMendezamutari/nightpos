<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\BuildsStaffAccessibleSiteIds;
use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class ListWaiterTablesController extends Controller
{
    use BuildsStaffAccessibleSiteIds;
    use ResolvesBranchSiteId;

    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $waiterId = (int) $user->id;

        $siteId = $this->resolveSiteIdForWaiterAssignments($request, $waiterId);
        if (! $siteId) {
            return response()->json(['message' => 'No se pudo resolver sucursal.'], 422);
        }

        $tables = DB::table('site_table_assignments')
            ->join('site_tables', 'site_tables.id', '=', 'site_table_assignments.site_table_id')
            ->leftJoin('site_rooms', 'site_rooms.id', '=', 'site_tables.site_room_id')
            ->where('site_tables.site_id', (int) $siteId)
            ->where('site_table_assignments.waiter_user_id', $waiterId)
            ->orderBy('site_tables.sort_order')
            ->orderBy('site_tables.code')
            ->get([
                'site_tables.id',
                'site_tables.code',
                'site_tables.seats',
                'site_tables.is_active',
                'site_rooms.name as room_name',
                'site_rooms.code as room_code',
            ])
            ->map(static function ($t) use ($siteId): array {
                $openSession = DB::table('customer_sessions')
                    ->where('site_id', (int) $siteId)
                    ->where('status', 'open')
                    ->where('table_code', $t->code)
                    ->orderByDesc('id')
                    ->first(['id', 'opened_at']);

                return [
                    'table_id' => (int) $t->id,
                    'table_code' => $t->code,
                    'room_name' => $t->room_name,
                    'room_code' => $t->room_code,
                    'seats' => (int) ($t->seats ?? 0),
                    'is_active' => (bool) $t->is_active,
                    'occupied' => $openSession !== null,
                    'open_session_id' => $openSession ? (int) $openSession->id : null,
                    'opened_at' => $openSession?->opened_at,
                ];
            })
            ->values()
            ->all();

        return response()->json([
            'data' => $tables,
            'meta' => [
                'site_id' => (int) $siteId,
            ],
        ]);
    }

    /**
     * Sucursal donde este garzón tiene mesas asignadas, alineada con la sucursal "activa" cuando sea posible.
     * Si active_site_id apunta a una sede sin mesas pero otra sede accesible sí tiene, se usa esa (evita lista vacía).
     *
     * @return ?int
     */
    private function resolveSiteIdForWaiterAssignments(Request $request, int $waiterId): ?int
    {
        $user = $request->user();
        $accessible = $this->staffAccessibleSiteIds($user);
        if ($accessible === []) {
            return null;
        }

        $resolved = $this->resolveBranchSiteId($request);

        $sitesWithTables = DB::table('site_table_assignments')
            ->join('site_tables', 'site_tables.id', '=', 'site_table_assignments.site_table_id')
            ->where('site_table_assignments.waiter_user_id', $waiterId)
            ->whereIn('site_tables.site_id', $accessible)
            ->distinct()
            ->pluck('site_tables.site_id')
            ->map(static fn ($id) => (int) $id)
            ->values();

        if ($sitesWithTables->isEmpty()) {
            if ($resolved !== null && in_array((int) $resolved, $accessible, true)) {
                return (int) $resolved;
            }

            return $accessible[0] ?? null;
        }

        if ($resolved !== null && $sitesWithTables->contains((int) $resolved)) {
            return (int) $resolved;
        }

        if ($sitesWithTables->count() === 1) {
            return (int) $sitesWithTables->first();
        }

        if ($user->site_id && $sitesWithTables->contains((int) $user->site_id)) {
            return (int) $user->site_id;
        }

        return (int) $sitesWithTables->sort()->values()->first();
    }
}
