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

final class ListBranchRoomsController extends Controller
{
    use ResolvesBranchSiteId;

    private const KIND_LABELS = [
        'main' => 'Sala principal',
        'dance_floor' => 'Pista / pista de baile',
        'vip' => 'VIP / palcos',
        'bar' => 'Barra',
        'terrace' => 'Terraza / deck',
        'lounge' => 'Lounge / chill-out',
        'box' => 'Cabañas / boxes',
        'smoking' => 'Sector fumadores',
        'staff' => 'Personal / backstage',
        'other' => 'Otro',
    ];

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

        $rows = DB::table('site_rooms')
            ->where('site_id', $siteId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function ($row) {
                $kind = $row->kind;

                return [
                    'id' => (int) $row->id,
                    'site_id' => (int) $row->site_id,
                    'code' => $row->code,
                    'name' => $row->name,
                    'kind' => $kind,
                    'kind_label' => self::KIND_LABELS[$kind] ?? $kind,
                    'floor_label' => $row->floor_label,
                    'capacity_estimate' => $row->capacity_estimate !== null ? (int) $row->capacity_estimate : null,
                    'sort_order' => (int) $row->sort_order,
                    'is_active' => (bool) $row->is_active,
                ];
            });

        return response()->json([
            'data' => [
                'site_id' => $siteId,
                'rooms' => $rows,
                'kind_options' => collect(self::KIND_LABELS)->map(fn (string $label, string $key) => [
                    'value' => $key,
                    'label' => $label,
                ])->values(),
            ],
        ]);
    }
}
