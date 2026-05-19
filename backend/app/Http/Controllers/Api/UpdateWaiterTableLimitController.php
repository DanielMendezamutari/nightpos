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

final class UpdateWaiterTableLimitController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(int $userId, Request $request): JsonResponse
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

        $payload = $request->validate([
            'max_active_tables' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $waiter = DB::table('users')
            ->where('id', $userId)
            ->where('site_id', $siteId)
            ->where('role', 'waiter')
            ->first();
        if ($waiter === null) {
            return response()->json(['message' => 'Garzon no encontrado en esta sucursal.'], Response::HTTP_NOT_FOUND);
        }

        DB::table('users')
            ->where('id', $userId)
            ->update([
                'max_active_tables' => (int) $payload['max_active_tables'],
                'updated_at' => now(),
            ]);

        return response()->json([
            'data' => [
                'user_id' => (int) $userId,
                'max_active_tables' => (int) $payload['max_active_tables'],
            ],
        ]);
    }
}
