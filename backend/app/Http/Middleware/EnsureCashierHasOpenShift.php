<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class EnsureCashierHasOpenShift
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || $user->role !== 'cashier') {
            return $next($request);
        }

        $routeShiftId = (int) ($request->route('shiftTurnId') ?? 0);
        $bodyShiftId = (int) $request->integer('shift_turn_id');
        $targetShiftId = $routeShiftId > 0 ? $routeShiftId : ($bodyShiftId > 0 ? $bodyShiftId : 0);

        $siteId = $user->active_site_id ?: $user->site_id;
        if (! $siteId) {
            return response()->json([
                'message' => 'El cajero no tiene sucursal asignada.',
                'requires_open_shift' => true,
            ], 428);
        }

        // Liquidación PDF del turno (abierto o cerrado) de la sucursal del cajero.
        if ($targetShiftId > 0 && $request->isMethod('GET') && str_ends_with('/'.$request->path(), '/pdf')) {
            $belongs = DB::table('shift_turns')
                ->where('id', $targetShiftId)
                ->where('site_id', (int) $siteId)
                ->exists();
            if ($belongs) {
                return $next($request);
            }
        }

        if ($targetShiftId > 0) {
            $openTargetShift = DB::table('shift_turns')
                ->where('id', $targetShiftId)
                ->where('status', 'open')
                ->exists();
            if ($openTargetShift) {
                return $next($request);
            }
        }

        $openShift = DB::table('shift_turns')
            ->where('site_id', (int) $siteId)
            ->where('status', 'open')
            ->exists();

        if (! $openShift) {
            return response()->json([
                'message' => 'Debe abrir caja antes de operar.',
                'requires_open_shift' => true,
            ], 428);
        }

        return $next($request);
    }
}

