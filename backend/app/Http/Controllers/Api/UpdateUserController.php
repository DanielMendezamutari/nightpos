<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\WaiterCommissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;

final class UpdateUserController extends Controller
{
    public function __invoke(int $userId, Request $request): JsonResponse
    {
        $actor = $request->user();
        if (! $actor) {
            return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        $target = User::query()->find($userId);
        if (! $target) {
            return response()->json(['message' => 'Usuario no encontrado.'], Response::HTTP_NOT_FOUND);
        }

        $payload = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'pin_code' => ['nullable', 'digits_between:4,8', Rule::unique('users', 'pin_code')->ignore($userId)],
            'role' => ['sometimes', 'in:owner,super_admin,admin,cashier,waiter,manager'],
            'password' => ['sometimes', 'nullable', 'string', 'min:8'],
            'default_site_id' => ['nullable', 'integer', 'exists:sites,id'],
            'site_ids' => ['sometimes', 'array', 'min:1'],
            'site_ids.*' => ['integer', 'exists:sites,id'],
            'waiter_compensation_type' => ['sometimes', 'nullable', Rule::in(['per_payment', 'payroll_monthly', 'payroll_weekly'])],
            'waiter_commission_rate_pct' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        if ($actor->role === 'admin') {
            $actorSiteId = (int) ($actor->active_site_id ?: $actor->site_id ?: 0);
            if (! $actorSiteId || (int) $target->site_id !== $actorSiteId) {
                return response()->json(['message' => 'Admin solo puede editar usuarios de su sucursal activa.'], Response::HTTP_FORBIDDEN);
            }
            if (! in_array($target->role, ['cashier', 'waiter', 'manager'], true)) {
                return response()->json(['message' => 'Admin solo puede editar personal de sucursal.'], Response::HTTP_FORBIDDEN);
            }
            if (isset($payload['role']) && ! in_array($payload['role'], ['cashier', 'waiter', 'manager'], true)) {
                return response()->json(['message' => 'Admin solo puede usar roles de personal de sucursal.'], Response::HTTP_FORBIDDEN);
            }
            if (isset($payload['site_ids']) || isset($payload['default_site_id'])) {
                return response()->json(['message' => 'Admin no puede mover usuarios entre sucursales.'], Response::HTTP_FORBIDDEN);
            }
        }

        if (isset($payload['name'])) {
            $target->name = $payload['name'];
        }
        if (isset($payload['email'])) {
            $target->email = $payload['email'];
        }
        if (array_key_exists('pin_code', $payload)) {
            $target->pin_code = $payload['pin_code'];
        }
        if (isset($payload['role'])) {
            $target->role = $payload['role'];
        }
        if (isset($payload['password']) && $payload['password'] !== '') {
            $target->password = Hash::make($payload['password']);
        }

        $siteIds = collect($payload['site_ids'] ?? [])->map(fn ($v) => (int) $v)->filter()->unique()->values();
        $defaultSiteId = isset($payload['default_site_id']) ? (int) $payload['default_site_id'] : null;
        if ($siteIds->isNotEmpty() && ! $defaultSiteId) {
            $defaultSiteId = (int) $siteIds->first();
        }
        if ($defaultSiteId && ! $siteIds->contains($defaultSiteId)) {
            $siteIds->push($defaultSiteId);
            $siteIds = $siteIds->unique()->values();
        }

        if ($siteIds->isNotEmpty()) {
            DB::table('user_site_accesses')->where('user_id', $target->id)->delete();
            foreach ($siteIds as $siteId) {
                DB::table('user_site_accesses')->insert([
                    'user_id' => $target->id,
                    'site_id' => (int) $siteId,
                    'is_default' => (int) $siteId === (int) $defaultSiteId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $target->site_id = $defaultSiteId;
            if (! $target->active_site_id || ! $siteIds->contains((int) $target->active_site_id)) {
                $target->active_site_id = $defaultSiteId;
            }
        } elseif ($defaultSiteId !== null) {
            $target->site_id = $defaultSiteId;
            if (! $target->active_site_id) {
                $target->active_site_id = $defaultSiteId;
            }
        }

        if (isset($payload['role']) && $payload['role'] !== 'waiter') {
            $target->waiter_compensation_type = 'per_payment';
            $target->waiter_commission_rate_pct = null;
        }
        if (isset($payload['waiter_compensation_type']) && ($target->role === 'waiter' || (isset($payload['role']) && $payload['role'] === 'waiter'))) {
            $target->waiter_compensation_type = $payload['waiter_compensation_type'] ?? 'per_payment';
        }

        $finalRole = $target->role;
        $finalComp = $target->waiter_compensation_type ?? 'per_payment';
        if ($finalRole === 'waiter' && array_key_exists('waiter_commission_rate_pct', $payload)) {
            $rawPct = $payload['waiter_commission_rate_pct'];
            if ($finalComp !== 'per_payment' || $rawPct === null || $rawPct === '') {
                $target->waiter_commission_rate_pct = null;
            } else {
                $target->waiter_commission_rate_pct = round((float) $rawPct, 2);
            }
        } elseif ($finalRole === 'waiter' && isset($payload['waiter_compensation_type']) && $finalComp !== 'per_payment') {
            $target->waiter_commission_rate_pct = null;
        }

        $target->save();

        if ($target->role === 'waiter' && (
            array_key_exists('waiter_compensation_type', $payload) || array_key_exists('waiter_commission_rate_pct', $payload)
        )) {
            WaiterCommissionService::recalculateStoredCommissionsForWaiter($target->id);
        }

        if (isset($payload['role'])) {
            Role::firstOrCreate(['name' => $payload['role'], 'guard_name' => 'api']);
            $target->syncRoles([$payload['role']]);
        }

        return response()->json([
            'data' => [
                'id' => $target->id,
                'name' => $target->name,
                'email' => $target->email,
                'role' => $target->role,
                'site_id' => $target->site_id,
                'active_site_id' => $target->active_site_id,
                'waiter_compensation_type' => $target->waiter_compensation_type,
                'waiter_commission_rate_pct' => $target->waiter_commission_rate_pct,
            ],
        ]);
    }
}
