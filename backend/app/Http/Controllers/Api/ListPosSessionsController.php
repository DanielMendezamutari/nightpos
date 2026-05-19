<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class ListPosSessionsController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(Request $request): JsonResponse
    {
        $siteId = $this->resolveBranchSiteId($request);

        $rows = DB::table('customer_sessions')
            ->where('status', 'open')
            ->when($siteId, fn ($q) => $q->where('site_id', (int) $siteId))
            ->orderByDesc('id')
            ->get()
            ->map(static function ($r): array {
                return [
                    'id' => (int) $r->id,
                    'site_id' => (int) $r->site_id,
                    'table_code' => $r->table_code,
                    'zone_code' => $r->zone_code,
                    'status' => $r->status,
                    'opened_at' => $r->opened_at,
                ];
            })
            ->values()
            ->all();

        return response()->json(['data' => $rows]);
    }
}

