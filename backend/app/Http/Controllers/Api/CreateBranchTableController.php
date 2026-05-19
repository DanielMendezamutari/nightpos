<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

final class CreateBranchTableController extends Controller
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

        $payload = $request->validate([
            'site_room_id' => ['nullable', 'integer', 'exists:site_rooms,id'],
            'prefix' => ['required', 'string', 'max:16', 'regex:/^[A-Za-z0-9_-]+$/'],
            'quantity' => ['required', 'integer', 'min:1', 'max:200'],
            'start_number' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'seats' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $roomId = $payload['site_room_id'] ?? null;
        if ($roomId !== null) {
            $roomBelongsToSite = DB::table('site_rooms')
                ->where('id', $roomId)
                ->where('site_id', $siteId)
                ->exists();
            if (! $roomBelongsToSite) {
                return response()->json([
                    'message' => 'La sala seleccionada no pertenece a esta sucursal.',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $prefix = strtoupper(trim($payload['prefix']));
        $quantity = (int) $payload['quantity'];
        $start = (int) ($payload['start_number'] ?? 1);
        $seats = (int) ($payload['seats'] ?? 4);
        $codes = [];
        for ($i = 0; $i < $quantity; $i++) {
            $codes[] = $prefix.'-'.($start + $i);
        }

        $existing = DB::table('site_tables')
            ->where('site_id', $siteId)
            ->whereIn('code', $codes)
            ->pluck('code')
            ->all();
        if ($existing !== []) {
            throw ValidationException::withMessages([
                'quantity' => ['Ya existen mesas con esos códigos: '.implode(', ', $existing)],
            ]);
        }

        $created = [];
        $nextSort = ((int) DB::table('site_tables')->where('site_id', $siteId)->max('sort_order')) + 10;
        foreach ($codes as $i => $code) {
            $id = DB::table('site_tables')->insertGetId([
                'site_id' => $siteId,
                'site_room_id' => $roomId,
                'code' => $code,
                'seats' => $seats,
                'sort_order' => $nextSort + ($i * 10),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $created[] = ['id' => $id, 'code' => $code];
        }

        return response()->json([
            'data' => [
                'created_count' => count($created),
                'tables' => $created,
            ],
        ], Response::HTTP_CREATED);
    }
}
