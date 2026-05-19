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

final class DeleteBranchTableController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(int $tableId, Request $request): JsonResponse
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

        $row = DB::table('site_tables')
            ->where('id', $tableId)
            ->where('site_id', $siteId)
            ->first();
        if ($row === null) {
            return response()->json(['message' => 'Mesa no encontrada en esta sucursal.'], Response::HTTP_NOT_FOUND);
        }

        $inUse = DB::table('customer_sessions')
            ->where('site_id', $siteId)
            ->where('table_code', $row->code)
            ->where('status', 'open')
            ->exists();
        if ($inUse) {
            return response()->json([
                'message' => 'No se puede borrar la mesa porque tiene consumo/sesion abierta.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::table('site_tables')
            ->where('id', $tableId)
            ->where('site_id', $siteId)
            ->delete();

        DB::table('site_table_assignments')
            ->where('site_id', $siteId)
            ->where('site_table_id', $tableId)
            ->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
