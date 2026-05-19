<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Concerns\ValidatesWaiterBranchAccess;
use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class AssignBranchTableController extends Controller
{
    use ResolvesBranchSiteId;
    use ValidatesWaiterBranchAccess;

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

        $payload = $request->validate([
            'waiter_user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $table = DB::table('site_tables')
            ->where('id', $tableId)
            ->where('site_id', $siteId)
            ->first();
        if ($table === null) {
            return response()->json(['message' => 'Mesa no encontrada en esta sucursal.'], Response::HTTP_NOT_FOUND);
        }

        $waiter = DB::table('users')
            ->where('id', $payload['waiter_user_id'])
            ->where('role', 'waiter')
            ->first();
        if ($waiter === null || ! $this->waiterWorksAtBranch((int) $waiter->id, (int) $siteId)) {
            return response()->json([
                'message' => 'El usuario seleccionado no es un garzon habilitado para esta sucursal.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $current = DB::table('site_table_assignments')
            ->where('site_table_id', $tableId)
            ->first();

        $assignedCount = DB::table('site_table_assignments')
            ->where('site_id', $siteId)
            ->where('waiter_user_id', $waiter->id)
            ->when($current !== null && (int) $current->waiter_user_id === (int) $waiter->id, function ($q) use ($tableId) {
                $q->where('site_table_id', '!=', $tableId);
            })
            ->count();

        $limit = (int) ($waiter->max_active_tables ?? 5);
        if ($assignedCount >= $limit) {
            return response()->json([
                'message' => "Este garzon ya llego a su limite de {$limit} mesas.",
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($current === null) {
            DB::table('site_table_assignments')->insert([
                'site_id' => $siteId,
                'site_table_id' => $tableId,
                'waiter_user_id' => $waiter->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('site_table_assignments')
                ->where('site_table_id', $tableId)
                ->update([
                    'waiter_user_id' => $waiter->id,
                    'updated_at' => now(),
                ]);
        }

        return response()->json([
            'data' => [
                'table_id' => $tableId,
                'waiter_user_id' => (int) $waiter->id,
                'waiter_name' => $waiter->name,
            ],
        ]);
    }
}
