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

final class CreateBranchContactController extends Controller
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
            'contact_type' => ['required', 'in:client,companion,supplier'],
            'display_name' => ['required', 'string', 'max:140'],
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

        $id = DB::table('site_contacts')->insertGetId([
            'site_id' => $siteId,
            'contact_type' => $payload['contact_type'],
            'display_name' => $payload['display_name'],
            'phone' => $payload['phone'] ?? null,
            'email' => $payload['email'] ?? null,
            'document_type' => $payload['document_type'] ?? null,
            'document_number' => $payload['document_number'] ?? null,
            'business_name' => $payload['business_name'] ?? null,
            'service_category' => $payload['service_category'] ?? null,
            'commission_percent' => $payload['commission_percent'] ?? null,
            'notes' => $payload['notes'] ?? null,
            'is_active' => $payload['is_active'] ?? true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'data' => [
                'id' => $id,
                'contact_type' => $payload['contact_type'],
                'display_name' => $payload['display_name'],
            ],
        ], Response::HTTP_CREATED);
    }
}
