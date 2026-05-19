<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\BuildsStaffAccessibleSiteIds;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

final class AuthController extends Controller
{
    use BuildsStaffAccessibleSiteIds;

    public function login(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'email' => ['nullable', 'email'],
            'password' => ['nullable', 'string'],
            'pin' => ['nullable', 'digits_between:4,8'],
        ]);

        $user = null;
        if (! empty($payload['pin'])) {
            $user = User::query()->where('pin_code', $payload['pin'])->first();
        } elseif (! empty($payload['email']) && ! empty($payload['password'])) {
            $user = User::query()->where('email', $payload['email'])->first();
            if ($user && ! Hash::check((string) $payload['password'], $user->password)) {
                $user = null;
            }
        }

        if (! $user) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        $sites = $this->resolveAccessibleSites($user);
        if (count($sites) === 1 && ! $user->active_site_id) {
            $user->active_site_id = $sites[0]['id'];
            $user->save();
            $user->refresh();
        }

        $token = JWTAuth::fromUser($user);
        $requiresOpenShift = $this->computeRequiresOpenShift($user);

        return response()->json([
            'data' => [
                'token' => $token,
                'user' => $this->serializeUser($user),
                'requires_open_shift' => $requiresOpenShift,
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->serializeUser($request->user()),
        ]);
    }

    public function updateMe(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        $payload = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'pin' => ['sometimes', 'nullable', 'digits_between:4,8', Rule::unique('users', 'pin_code')->ignore($user->id)],
            'password' => ['sometimes', 'string', 'min:8'],
        ]);

        if (isset($payload['name'])) {
            $user->name = $payload['name'];
        }
        if (isset($payload['email'])) {
            $user->email = $payload['email'];
        }
        if (array_key_exists('pin', $payload)) {
            $user->pin_code = $payload['pin'] ?: null;
        }
        if (isset($payload['password']) && $payload['password'] !== '') {
            $user->password = $payload['password'];
        }

        $user->save();

        return response()->json([
            'data' => $this->serializeUser($user->fresh()),
        ]);
    }

    public function siteOptions(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        return response()->json([
            'data' => [
                'active_site_id' => $user->active_site_id,
                'sites' => $this->resolveAccessibleSites($user),
            ],
        ]);
    }

    public function setActiveSite(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        $payload = $request->validate([
            'site_id' => ['required', 'integer', 'exists:sites,id'],
        ]);

        $targetSiteId = (int) $payload['site_id'];
        $allowed = collect($this->resolveAccessibleSites($user))
            ->contains(fn ($s) => (int) $s['id'] === $targetSiteId);
        if (! $allowed && $user->role === 'waiter') {
            $allowed = DB::table('site_table_assignments')
                ->join('site_tables', 'site_tables.id', '=', 'site_table_assignments.site_table_id')
                ->where('site_table_assignments.waiter_user_id', $user->id)
                ->where('site_tables.site_id', $targetSiteId)
                ->exists();
        }
        if (! $allowed) {
            return response()->json(['message' => 'No tienes acceso a esa sucursal.'], Response::HTTP_FORBIDDEN);
        }

        $user->active_site_id = (int) $payload['site_id'];
        $user->save();
        $fresh = $user->fresh();

        return response()->json([
            'data' => [
                'active_site_id' => $fresh->active_site_id,
                'user' => $this->serializeUser($fresh),
                'requires_open_shift' => $this->computeRequiresOpenShift($fresh),
            ],
        ]);
    }

    public function logout(): JsonResponse
    {
        try {
            JWTAuth::parseToken()->invalidate();
        } catch (\Throwable) {
            //
        }

        return response()->json([
            'message' => 'Session closed.',
        ]);
    }

    /**
     * @return array<int, array{id:int,code:string,name:string}>
     */
    private function resolveAccessibleSites(User $user): array
    {
        if (in_array($user->role, ['owner', 'super_admin'], true)) {
            return DB::table('sites')
                ->orderBy('code')
                ->get(['id', 'code', 'name'])
                ->map(fn ($s) => ['id' => (int) $s->id, 'code' => $s->code, 'name' => $s->name])
                ->all();
        }

        $ids = $this->staffAccessibleSiteIds($user);
        if ($ids === []) {
            return [];
        }

        return DB::table('sites')
            ->whereIn('id', $ids)
            ->orderBy('code')
            ->get(['id', 'code', 'name'])
            ->map(fn ($s) => ['id' => (int) $s->id, 'code' => $s->code, 'name' => $s->name])
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'pin_code' => $user->pin_code,
            'role' => $user->role,
            'site_id' => $user->site_id,
            'active_site_id' => $user->active_site_id,
            'accessible_sites' => $this->resolveAccessibleSites($user),
        ];
    }

    private function computeRequiresOpenShift(User $user): bool
    {
        if ($user->role !== 'cashier') {
            return false;
        }

        $siteId = $this->resolvePrimarySiteId($user);
        if (! $siteId) {
            return true;
        }

        return ! DB::table('shift_turns')
            ->where('site_id', $siteId)
            ->where('status', 'open')
            ->exists();
    }

    private function resolvePrimarySiteId(User $user): ?int
    {
        if ($user->active_site_id) {
            return (int) $user->active_site_id;
        }
        if ($user->site_id) {
            return (int) $user->site_id;
        }

        $siteId = DB::table('user_site_accesses')
            ->where('user_id', $user->id)
            ->where('is_default', true)
            ->value('site_id');

        if ($siteId) {
            return (int) $siteId;
        }

        $first = DB::table('user_site_accesses')
            ->where('user_id', $user->id)
            ->value('site_id');

        return $first ? (int) $first : null;
    }
}
