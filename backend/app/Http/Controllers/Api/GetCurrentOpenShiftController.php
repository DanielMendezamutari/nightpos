<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class GetCurrentOpenShiftController extends Controller
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

        $row = DB::table('shift_turns')
            ->where('site_id', $siteId)
            ->where('status', 'open')
            ->orderByDesc('id')
            ->first();

        if (! $row) {
            return response()->json(['data' => null]);
        }

        return response()->json([
            'data' => [
                'id' => (int) $row->id,
                'site_id' => (int) $row->site_id,
                'cashier_user_id' => (int) $row->cashier_user_id,
                'period' => $row->period,
                'opening_cash' => (int) $row->opening_cash,
                'status' => $row->status,
                'opened_at' => $row->opened_at,
            ],
        ]);
    }
}
