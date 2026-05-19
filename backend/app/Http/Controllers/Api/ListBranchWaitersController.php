<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use App\Support\WaiterCommissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class ListBranchWaitersController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(Request $request): JsonResponse
    {
        $siteId = $this->resolveBranchSiteId($request);
        if ($siteId === null) {
            return response()->json([
                'message' => 'No se pudo determinar la sucursal. Si sos administrador global, enviá ?site_id= en la URL.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $rows = DB::table('users')
            ->where('role', 'waiter')
            ->where(function ($q) use ($siteId): void {
                $q->where('site_id', $siteId)
                    ->orWhereExists(function ($sub) use ($siteId): void {
                        $sub->selectRaw('1')
                            ->from('user_site_accesses as usa')
                            ->whereColumn('usa.user_id', 'users.id')
                            ->where('usa.site_id', $siteId);
                    });
            })
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'waiter_compensation_type',
                'waiter_commission_rate_pct',
            ]);

        $waiters = $rows->map(static function ($row): array {
            return [
                'id' => (int) $row->id,
                'name' => $row->name,
                'waiter_compensation_type' => $row->waiter_compensation_type ?? 'per_payment',
                'waiter_commission_rate_pct' => $row->waiter_commission_rate_pct !== null
                    ? round((float) $row->waiter_commission_rate_pct, 2)
                    : null,
            ];
        })->values()->all();

        return response()->json([
            'data' => [
                'waiters' => $waiters,
                'default_commission_rate_pct' => WaiterCommissionService::currentRatePct(),
            ],
        ]);
    }
}
