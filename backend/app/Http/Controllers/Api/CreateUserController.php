<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

final class CreateUserController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'pin_code' => ['nullable', 'digits_between:4,8', 'unique:users,pin_code'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:owner,super_admin,admin,cashier,waiter,manager'],
            'site_id' => ['nullable', 'integer', 'exists:sites,id'],
            'site_ids' => ['sometimes', 'array', 'min:1'],
            'site_ids.*' => ['integer', 'exists:sites,id'],
            'default_site_id' => ['nullable', 'integer', 'exists:sites,id'],
            'waiter_compensation_type' => ['nullable', Rule::in(['per_payment', 'payroll_monthly', 'payroll_weekly'])],
            'waiter_commission_rate_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $actor = $request->user();

        if (! $actor) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $requestedSiteIds = collect($payload['site_ids'] ?? [])
            ->map(fn ($v) => (int) $v)
            ->filter()
            ->values();

        $singleSiteId = isset($payload['site_id']) ? (int) $payload['site_id'] : null;
        if ($singleSiteId) {
            $requestedSiteIds->push($singleSiteId);
        }
        $requestedSiteIds = $requestedSiteIds->unique()->values();

        $defaultSiteId = isset($payload['default_site_id']) ? (int) $payload['default_site_id'] : null;
        if (! $defaultSiteId && $singleSiteId) {
            $defaultSiteId = $singleSiteId;
        }
        if (! $defaultSiteId && $requestedSiteIds->isNotEmpty()) {
            $defaultSiteId = (int) $requestedSiteIds->first();
        }

        if ($actor->role === 'admin') {
            $allowedRoles = ['cashier', 'waiter', 'manager'];

            if (! in_array($payload['role'], $allowedRoles, true)) {
                return response()->json(['message' => 'Admin solo puede crear cajera, garzon o encargada.'], 403);
            }

            $actorSiteId = (int) ($actor->active_site_id ?: $actor->site_id ?: 0);
            if (! $actorSiteId) {
                return response()->json(['message' => 'No tienes sucursal activa para crear personal.'], 403);
            }

            if ($requestedSiteIds->isEmpty()) {
                $requestedSiteIds = collect([$actorSiteId]);
            }
            if ($requestedSiteIds->count() !== 1 || (int) $requestedSiteIds->first() !== $actorSiteId) {
                return response()->json(['message' => 'Admin solo puede crear personal en su sucursal activa.'], 403);
            }
            $defaultSiteId = $actorSiteId;
        }

        if ($actor->role === 'super_admin') {
            if (in_array($payload['role'], ['owner', 'super_admin'], true)) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }
        }

        if (in_array($payload['role'], ['cashier', 'waiter', 'manager', 'admin'], true) && ! $defaultSiteId) {
            return response()->json(['message' => 'Debes elegir una sucursal principal para este usuario.'], 422);
        }

        $waiterComp = $payload['waiter_compensation_type'] ?? 'per_payment';
        if ($payload['role'] !== 'waiter') {
            $waiterComp = 'per_payment';
        }

        $waiterPct = null;
        if ($payload['role'] === 'waiter' && $waiterComp === 'per_payment' && array_key_exists('waiter_commission_rate_pct', $payload)) {
            $rp = $payload['waiter_commission_rate_pct'];
            $waiterPct = ($rp !== null && $rp !== '') ? round((float) $rp, 2) : null;
        }

        $user = User::query()->create([
            'name' => $payload['name'],
            'email' => $payload['email'],
            'pin_code' => $payload['pin_code'] ?? null,
            'password' => Hash::make($payload['password']),
            'role' => $payload['role'],
            'site_id' => $defaultSiteId,
            'active_site_id' => $defaultSiteId,
            'waiter_compensation_type' => $waiterComp,
            'waiter_commission_rate_pct' => $waiterPct,
        ]);

        Role::firstOrCreate(['name' => $payload['role'], 'guard_name' => 'api']);
        $user->syncRoles([$payload['role']]);

        if (in_array($payload['role'], ['admin', 'manager', 'cashier', 'waiter'], true) && $requestedSiteIds->isNotEmpty()) {
            foreach ($requestedSiteIds as $siteId) {
                DB::table('user_site_accesses')->insert([
                    'user_id' => $user->id,
                    'site_id' => (int) $siteId,
                    'is_default' => (int) $siteId === (int) $defaultSiteId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'site_id' => $user->site_id,
                'active_site_id' => $user->active_site_id,
                'waiter_compensation_type' => $user->waiter_compensation_type,
                'waiter_commission_rate_pct' => $user->waiter_commission_rate_pct,
            ],
        ], 201);
    }
}
