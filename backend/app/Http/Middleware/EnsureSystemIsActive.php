<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

final class EnsureSystemIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && in_array($user->role, ['owner', 'super_admin'], true)) {
            return $next($request);
        }

        $setting = DB::table('system_settings')
            ->where('key', 'global_lock')
            ->first();

        if ($setting && $setting->is_locked) {
            return response()->json([
                'message' => 'Sistema bloqueado por el owner.',
                'reason' => $setting->reason,
            ], 423);
        }

        if ($user && $user->site_id) {
            $subscription = DB::table('saas_subscriptions')
                ->where('site_id', (int) $user->site_id)
                ->first();

            if ($subscription && $subscription->status === 'suspended') {
                return response()->json([
                    'message' => 'Sucursal suspendida por falta de pago SaaS.',
                    'reason' => $subscription->suspended_reason,
                ], 423);
            }
        }

        return $next($request);
    }
}
