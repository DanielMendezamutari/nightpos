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

final class UpdateBranchRoomController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(int $roomId, Request $request): JsonResponse
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

        $room = DB::table('site_rooms')
            ->where('id', $roomId)
            ->where('site_id', $siteId)
            ->first();

        if ($room === null) {
            return response()->json(['message' => 'Sala no encontrada en esta sucursal.'], Response::HTTP_NOT_FOUND);
        }

        $payload = $request->validate([
            'name' => ['sometimes', 'string', 'max:120'],
            'kind' => ['sometimes', 'in:main,dance_floor,vip,bar,terrace,lounge,box,smoking,staff,other'],
            'floor_label' => ['nullable', 'string', 'max:20'],
            'capacity_estimate' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if ($payload === []) {
            return response()->json(['data' => ['id' => $roomId]]);
        }

        DB::table('site_rooms')
            ->where('id', $roomId)
            ->where('site_id', $siteId)
            ->update([...$payload, 'updated_at' => now()]);

        return response()->json([
            'data' => [
                'id' => $roomId,
                ...$payload,
            ],
        ]);
    }
}
