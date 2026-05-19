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

final class DeleteBranchRoomController extends Controller
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

        $inUse = DB::table('customer_sessions')
            ->where('site_room_id', $roomId)
            ->exists();

        if ($inUse) {
            return response()->json([
                'message' => 'No se puede borrar: hay mesas o sesiones vinculadas a esta sala.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::table('site_rooms')
            ->where('id', $roomId)
            ->where('site_id', $siteId)
            ->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
