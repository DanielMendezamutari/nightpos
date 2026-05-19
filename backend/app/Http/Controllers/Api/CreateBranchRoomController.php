<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

final class CreateBranchRoomController extends Controller
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

        if ($request->has('code')) {
            $request->merge([
                'code' => strtoupper(str_replace('-', '_', (string) $request->input('code'))),
            ]);
        }

        $payload = $request->validate([
            'code' => [
                'required',
                'string',
                'max:32',
                'regex:/^[A-Z0-9_]+$/',
                Rule::unique('site_rooms', 'code')->where('site_id', $siteId),
            ],
            'name' => ['required', 'string', 'max:120'],
            'kind' => ['required', 'in:main,dance_floor,vip,bar,terrace,lounge,box,smoking,staff,other'],
            'floor_label' => ['nullable', 'string', 'max:20'],
            'capacity_estimate' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $code = $payload['code'];
        $sortOrder = $payload['sort_order'] ?? ((int) DB::table('site_rooms')->where('site_id', $siteId)->max('sort_order')) + 10;

        $id = DB::table('site_rooms')->insertGetId([
            'site_id' => $siteId,
            'code' => $code,
            'name' => $payload['name'],
            'kind' => $payload['kind'],
            'floor_label' => $payload['floor_label'] ?? null,
            'capacity_estimate' => $payload['capacity_estimate'] ?? null,
            'sort_order' => $sortOrder,
            'is_active' => $payload['is_active'] ?? true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'data' => [
                'id' => $id,
                'site_id' => $siteId,
                'code' => $code,
                'name' => $payload['name'],
                'kind' => $payload['kind'],
            ],
        ], Response::HTTP_CREATED);
    }
}
