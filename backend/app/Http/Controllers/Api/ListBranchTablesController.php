<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class ListBranchTablesController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(Request $request): JsonResponse
    {
        $siteId = $this->resolveBranchSiteId($request);
        if ($siteId === null) {
            return response()->json([
                'message' => 'No se pudo determinar la sucursal. Si eres super admin u owner, envia site_id en la URL.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (! Site::query()->whereKey($siteId)->exists()) {
            return response()->json(['message' => 'Sucursal no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        $rooms = DB::table('site_rooms')
            ->where('site_id', $siteId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        $tables = DB::table('site_tables')
            ->leftJoin('site_rooms', 'site_tables.site_room_id', '=', 'site_rooms.id')
            ->leftJoin('site_table_assignments', 'site_tables.id', '=', 'site_table_assignments.site_table_id')
            ->leftJoin('users as waiters', 'site_table_assignments.waiter_user_id', '=', 'waiters.id')
            ->where('site_tables.site_id', $siteId)
            ->orderBy('site_tables.sort_order')
            ->orderBy('site_tables.code')
            ->get([
                'site_tables.id',
                'site_tables.site_room_id',
                'site_tables.code',
                'site_tables.seats',
                'site_tables.sort_order',
                'site_tables.is_active',
                'site_rooms.name as room_name',
                'site_rooms.code as room_code',
                'site_table_assignments.waiter_user_id as assigned_waiter_user_id',
                'waiters.name as assigned_waiter_name',
            ]);

        $waiters = DB::table('users')
            ->leftJoin('site_table_assignments', function ($join) use ($siteId) {
                $join->on('users.id', '=', 'site_table_assignments.waiter_user_id')
                    ->where('site_table_assignments.site_id', '=', $siteId);
            })
            ->where('users.role', 'waiter')
            ->where(function ($q) use ($siteId) {
                $q->where('users.site_id', $siteId)
                    ->orWhereExists(function ($sub) use ($siteId) {
                        $sub->selectRaw('1')
                            ->from('user_site_accesses')
                            ->whereColumn('user_site_accesses.user_id', 'users.id')
                            ->where('user_site_accesses.site_id', $siteId);
                    });
            })
            ->groupBy('users.id', 'users.name', 'users.email', 'users.max_active_tables')
            ->orderBy('users.name')
            ->get([
                'users.id',
                'users.name',
                'users.email',
                'users.max_active_tables',
                DB::raw('COUNT(site_table_assignments.id) as assigned_tables_count'),
            ]);

        return response()->json([
            'data' => [
                'site_id' => $siteId,
                'rooms' => $rooms,
                'tables' => $tables,
                'waiters' => $waiters,
            ],
        ]);
    }
}
