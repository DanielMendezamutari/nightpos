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

final class ListBranchContactsController extends Controller
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

        $type = (string) $request->query('type', '');
        if (! in_array($type, ['client', 'companion', 'supplier'], true)) {
            return response()->json(['message' => 'Tipo invalido. Usa client, companion o supplier.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $q = DB::table('site_contacts')
            ->where('site_id', $siteId)
            ->where('contact_type', $type)
            ->orderByDesc('is_active')
            ->orderBy('display_name');

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $q->where(function ($w) use ($search) {
                $w->where('display_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('document_number', 'like', "%{$search}%")
                    ->orWhere('business_name', 'like', "%{$search}%");
            });
        }

        return response()->json([
            'data' => [
                'site_id' => $siteId,
                'type' => $type,
                'contacts' => $q->get(),
            ],
        ]);
    }
}
