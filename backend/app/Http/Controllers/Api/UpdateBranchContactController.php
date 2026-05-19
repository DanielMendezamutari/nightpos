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

final class UpdateBranchContactController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(int $contactId, Request $request): JsonResponse
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

        $exists = DB::table('site_contacts')
            ->where('id', $contactId)
            ->where('site_id', $siteId)
            ->exists();
        if (! $exists) {
            return response()->json(['message' => 'Contacto no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        $payload = $request->validate([
            'display_name' => ['sometimes', 'string', 'max:140'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:140'],
            'document_type' => ['nullable', 'string', 'max:20'],
            'document_number' => ['nullable', 'string', 'max:40'],
            'business_name' => ['nullable', 'string', 'max:160'],
            'service_category' => ['nullable', 'string', 'max:80'],
            'commission_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1500'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if ($payload !== []) {
            DB::table('site_contacts')
                ->where('id', $contactId)
                ->where('site_id', $siteId)
                ->update([...$payload, 'updated_at' => now()]);
        }

        return response()->json(['data' => ['id' => $contactId]]);
    }
}
