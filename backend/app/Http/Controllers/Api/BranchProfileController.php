<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

final class BranchProfileController extends Controller
{
    use ResolvesBranchSiteId;

    public function show(Request $request): JsonResponse
    {
        $siteId = $this->resolveBranchSiteId($request);
        if ($siteId === null) {
            return response()->json([
                'message' => 'No se pudo determinar la sucursal. Si eres super admin, envia el parametro site_id.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $site = Site::query()->find($siteId);
        if (! $site) {
            return response()->json(['message' => 'Sucursal no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $this->serializeSite($site)]);
    }

    public function update(Request $request): JsonResponse
    {
        $siteId = $this->resolveBranchSiteId($request);
        if ($siteId === null) {
            return response()->json([
                'message' => 'No se pudo determinar la sucursal. Si eres super admin, envia el parametro site_id.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $site = Site::query()->find($siteId);
        if (! $site) {
            return response()->json(['message' => 'Sucursal no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        $payload = $request->validate([
            'legal_document_type' => ['sometimes', 'nullable', 'string', 'max:80'],
            'legal_document_number' => ['sometimes', 'nullable', 'string', 'max:80'],
            'legal_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'branch_address' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'branch_phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'branch_email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'economic_activity' => ['sometimes', 'nullable', 'string', 'max:255'],
            'authorization_date' => ['sometimes', 'nullable', 'date'],
            'authorization_resolution' => ['sometimes', 'nullable', 'string', 'max:120'],
            'manager_document_type' => ['sometimes', 'nullable', 'string', 'max:80'],
            'manager_document_number' => ['sometimes', 'nullable', 'string', 'max:80'],
            'manager_full_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'currency_code' => ['sometimes', 'string', 'size:3'],
            'ticket_series_start' => ['sometimes', 'integer', 'min:1', 'max:9999999'],
            'boleta_series_start' => ['sometimes', 'integer', 'min:1', 'max:9999999'],
            'factura_series_start' => ['sometimes', 'integer', 'min:1', 'max:9999999'],
        ]);

        $site->fill($payload);
        $site->save();

        return response()->json(['data' => $this->serializeSite($site->fresh())]);
    }

    public function uploadLogo(Request $request): JsonResponse
    {
        $siteId = $this->resolveBranchSiteId($request);
        if ($siteId === null) {
            return response()->json([
                'message' => 'No se pudo determinar la sucursal. Si eres super admin, envia el parametro site_id.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $site = Site::query()->find($siteId);
        if (! $site) {
            return response()->json(['message' => 'Sucursal no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        $request->validate([
            'logo' => ['required', 'file', 'image', 'max:2048'],
        ]);

        $file = $request->file('logo');
        $ext = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'png';
        $dir = 'site-logos';
        if ($site->logo_path) {
            Storage::disk('public')->delete($site->logo_path);
        }
        $path = $file->storeAs($dir, $site->id.'.'.$ext, 'public');
        $site->logo_path = $path;
        $site->save();

        return response()->json(['data' => $this->serializeSite($site->fresh())]);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeSite(Site $site): array
    {
        $logoUrl = null;
        if ($site->logo_path) {
            $logoUrl = Storage::disk('public')->url($site->logo_path);
        }

        return [
            'id' => $site->id,
            'code' => $site->code,
            'name' => $site->name,
            'is_active' => $site->is_active,
            'legal_document_type' => $site->legal_document_type,
            'legal_document_number' => $site->legal_document_number,
            'legal_name' => $site->legal_name,
            'branch_address' => $site->branch_address,
            'branch_phone' => $site->branch_phone,
            'branch_email' => $site->branch_email,
            'economic_activity' => $site->economic_activity,
            'authorization_date' => $site->authorization_date?->format('Y-m-d'),
            'authorization_resolution' => $site->authorization_resolution,
            'manager_document_type' => $site->manager_document_type,
            'manager_document_number' => $site->manager_document_number,
            'manager_full_name' => $site->manager_full_name,
            'currency_code' => $site->currency_code,
            'ticket_series_start' => $site->ticket_series_start,
            'boleta_series_start' => $site->boleta_series_start,
            'factura_series_start' => $site->factura_series_start,
            'logo_path' => $site->logo_path,
            'logo_url' => $logoUrl,
        ];
    }
}
