<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\SSE\Repositories\SseTokenRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\SseTokenModel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

final class EloquentSseTokenRepository implements SseTokenRepositoryInterface
{
    public function create(
        int $tenantId,
        int $branchId,
        int $userId,
        ?string $roleScope,
        int $ttlSeconds = 60
    ): string {
        $token = Str::random(40);

        $tz = config('app.timezone', 'America/La_Paz');

        SseTokenModel::query()->create([
            'token'      => $token,
            'tenant_id'  => $tenantId,
            'branch_id'  => $branchId,
            'user_id'    => $userId,
            'role_scope' => $roleScope,
            'expires_at' => Carbon::now($tz)->addSeconds($ttlSeconds),
            'created_at' => Carbon::now($tz),
        ]);

        return $token;
    }

    public function findValid(string $token): ?array
    {
        $tz = config('app.timezone', 'America/La_Paz');

        $model = SseTokenModel::query()
            ->where('token', $token)
            ->where('expires_at', '>', Carbon::now($tz))
            ->first();

        if ($model === null) {
            return null;
        }

        return [
            'tenant_id'  => (int) $model->tenant_id,
            'branch_id'  => (int) $model->branch_id,
            'user_id'    => (int) $model->user_id,
            'role_scope' => $model->role_scope,
        ];
    }

    public function purgeExpired(): void
    {
        $tz = config('app.timezone', 'America/La_Paz');

        SseTokenModel::query()
            ->where('expires_at', '<=', Carbon::now($tz))
            ->delete();
    }
}
