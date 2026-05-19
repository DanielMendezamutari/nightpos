<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class EnsureAdminSiteScope
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'admin') {
            return $next($request);
        }

        $targetSiteId = $request->integer('site_id');

        if (! $targetSiteId && $request->route('shiftTurnId')) {
            $targetSiteId = (int) DB::table('shift_turns')
                ->where('id', (int) $request->route('shiftTurnId'))
                ->value('site_id');
        }

        if (! $targetSiteId) {
            return $next($request);
        }

        $activeSiteId = $user->active_site_id ?: $user->site_id;
        if (! $activeSiteId || (int) $activeSiteId !== $targetSiteId) {
            return response()->json(['message' => 'Forbidden for this branch.'], 403);
        }

        return $next($request);
    }
}
