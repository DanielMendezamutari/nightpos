<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class ListRecentShiftsForSiteController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(Request $request): JsonResponse
    {
        $siteId = $this->resolveBranchSiteId($request);
        if (! $siteId) {
            return response()->json([
                'message' => 'No se pudo determinar la sucursal. Si sos administrador global, enviá ?site_id= en la URL.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $limit = (int) $request->query('limit', 25);
        $limit = max(1, min(60, $limit));

        $rows = DB::table('shift_turns')
            ->where('site_id', $siteId)
            ->orderByDesc('id')
            ->limit($limit)
            ->get([
                'id',
                'status',
                'period',
                'opening_cash',
                'closing_cash',
                'opened_at',
                'closed_at',
            ]);

        return response()->json([
            'data' => $rows->map(static function ($r): array {
                return [
                    'id' => (int) $r->id,
                    'status' => $r->status,
                    'period' => $r->period,
                    'opening_cash' => (int) $r->opening_cash,
                    'closing_cash' => $r->closing_cash !== null ? (int) $r->closing_cash : null,
                    'opened_at' => $r->opened_at,
                    'closed_at' => $r->closed_at,
                ];
            }),
        ]);
    }
}
