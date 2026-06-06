<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\SSE\Repositories\OperationalEventRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\OperationalEventModel;
use Illuminate\Support\Carbon;

final class EloquentOperationalEventRepository implements OperationalEventRepositoryInterface
{
    public function create(
        int $tenantId,
        int $branchId,
        string $type,
        array $payload,
        ?string $targetRole = null
    ): array {
        $model = OperationalEventModel::query()->create([
            'tenant_id'   => $tenantId,
            'branch_id'   => $branchId,
            'type'        => $type,
            'target_role' => $targetRole,
            'payload'     => $payload,
            'created_at'  => Carbon::now(config('app.timezone', 'America/La_Paz')),
        ]);

        return $this->toArray($model);
    }

    public function findSince(
        int $tenantId,
        int $branchId,
        ?string $roleScope,
        int $lastId,
        int $limit = 50
    ): array {
        return OperationalEventModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('id', '>', $lastId)
            // roleScope = null means admin: no target_role filter (sees all)
            // roleScope = 'X' means specific role: broadcast (null) OR own role
            ->when($roleScope !== null, function ($q) use ($roleScope) {
                $q->where(function ($inner) use ($roleScope) {
                    $inner->whereNull('target_role')
                        ->orWhere('target_role', $roleScope);
                });
            })
            ->orderBy('id')
            ->limit($limit)
            ->get()
            ->map(fn (OperationalEventModel $m) => $this->toArray($m))
            ->all();
    }

    private function toArray(OperationalEventModel $model): array
    {
        return [
            'id'          => $model->id,
            'tenant_id'   => $model->tenant_id,
            'branch_id'   => $model->branch_id,
            'type'        => $model->type,
            'target_role' => $model->target_role,
            'payload'     => $model->payload,
            'created_at'  => $model->created_at?->toIso8601String(),
        ];
    }
}
