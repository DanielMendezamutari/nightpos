<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class ListUsersController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $actor = $request->user();
        if (! $actor) {
            return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        $query = DB::table('users')
            ->leftJoin('sites as home_site', 'users.site_id', '=', 'home_site.id')
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'users.pin_code',
                'users.role',
                'users.site_id',
                'users.active_site_id',
                'users.max_active_tables',
                'users.waiter_compensation_type',
                'users.waiter_commission_rate_pct',
                'home_site.code as site_code',
                'home_site.name as site_name',
            ])
            ->orderBy('users.name');

        if ($actor->role === 'admin') {
            $siteId = (int) ($actor->active_site_id ?: $actor->site_id ?: 0);
            if (! $siteId) {
                return response()->json(['message' => 'No tienes sucursal activa.'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $query->where('users.site_id', $siteId)
                ->whereIn('users.role', ['cashier', 'waiter', 'manager']);
        } elseif (in_array($actor->role, ['owner', 'super_admin'], true)) {
            $siteId = $request->integer('site_id');
            if ($siteId) {
                $query->where('users.site_id', $siteId);
            }
        } else {
            return response()->json(['message' => 'Forbidden.'], Response::HTTP_FORBIDDEN);
        }

        $rows = $query->get();

        $userIds = $rows->pluck('id')->map(fn ($id) => (int) $id)->all();
        $accessByUser = collect();
        if ($userIds !== []) {
            $accessRows = DB::table('user_site_accesses')
                ->join('sites', 'sites.id', '=', 'user_site_accesses.site_id')
                ->whereIn('user_site_accesses.user_id', $userIds)
                ->orderBy('sites.code')
                ->get([
                    'user_site_accesses.user_id',
                    'user_site_accesses.site_id',
                    'sites.code as access_code',
                ]);
            $accessByUser = $accessRows->groupBy('user_id');
        }

        $data = $rows->map(function ($row) use ($accessByUser): array {
            $uid = (int) $row->id;
            $access = $accessByUser->get($uid, collect());
            $siteIds = $access->pluck('site_id')->map(fn ($id) => (int) $id)->values()->all();
            $codes = $access->pluck('access_code')->filter()->values()->all();
            if ($siteIds === [] && $row->site_id) {
                $siteIds = [(int) $row->site_id];
                if ($row->site_code) {
                    $codes = [(string) $row->site_code];
                }
            }

            $siteAccessLabel = $codes !== [] ? implode(', ', $codes) : (string) ($row->site_code ?? '');

            return [
                'id' => $uid,
                'name' => $row->name,
                'email' => $row->email,
                'pin_code' => $row->pin_code,
                'role' => $row->role,
                'site_id' => $row->site_id ? (int) $row->site_id : null,
                'active_site_id' => $row->active_site_id ? (int) $row->active_site_id : null,
                'max_active_tables' => $row->max_active_tables !== null ? (int) $row->max_active_tables : null,
                'waiter_compensation_type' => $row->waiter_compensation_type ?? 'per_payment',
                'waiter_commission_rate_pct' => $row->waiter_commission_rate_pct !== null
                    ? round((float) $row->waiter_commission_rate_pct, 2)
                    : null,
                'site_code' => $row->site_code,
                'site_name' => $row->site_name,
                'site_ids' => $siteIds,
                'site_access_label' => $siteAccessLabel,
            ];
        })->values()->all();

        return response()->json([
            'data' => $data,
        ]);
    }
}
