<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

trait EnsuresShiftBelongsToResolvedSite
{
    use ResolvesBranchSiteId;

    protected function ensureShiftBelongsToResolvedSite(Request $request, int $shiftTurnId): ?JsonResponse
    {
        $resolved = $this->resolveBranchSiteId($request);
        if (! $resolved) {
            return response()->json([
                'message' => 'No se pudo determinar la sucursal. Si sos administrador global, enviá ?site_id= en la URL.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $shiftSite = (int) DB::table('shift_turns')->where('id', $shiftTurnId)->value('site_id');
        if (! $shiftSite) {
            return response()->json(['message' => 'Turno no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        if ($shiftSite !== $resolved) {
            return response()->json(['message' => 'El turno no pertenece a la sucursal activa.'], Response::HTTP_FORBIDDEN);
        }

        return null;
    }
}
