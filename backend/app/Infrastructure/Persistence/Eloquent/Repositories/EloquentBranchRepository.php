<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Branch\Entities\Branch;
use App\Domain\Branch\Repositories\BranchRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;

final class EloquentBranchRepository implements BranchRepositoryInterface
{
    public function findById(int $id): ?Branch
    {
        $model = BranchModel::query()->find($id);

        return $model ? $this->map($model) : null;
    }

    public function findByTenantAndCode(int $tenantId, string $code): ?Branch
    {
        $model = BranchModel::query()
            ->where('tenant_id', $tenantId)
            ->where('code', $code)
            ->first();

        return $model ? $this->map($model) : null;
    }

    public function listByTenant(int $tenantId): array
    {
        return BranchModel::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get()
            ->map(fn (BranchModel $model) => $this->map($model))
            ->all();
    }

    public function listAccessibleForUser(int $userId, int $tenantId): array
    {
        $user = \App\Infrastructure\Persistence\Eloquent\Models\UserModel::query()
            ->with(['accessibleBranches'])
            ->find($userId);

        if ($user === null) {
            return [];
        }

        if ($user->isSuperAdmin()) {
            return $this->listByTenant($tenantId);
        }

        $branchIds = $user->accessibleBranches->pluck('id')->map(fn ($id) => (int) $id)->all();

        if ($user->branch_id !== null) {
            $branchIds[] = (int) $user->branch_id;
        }

        $branchIds = array_values(array_unique($branchIds));

        if ($branchIds === []) {
            return [];
        }

        return BranchModel::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('id', $branchIds)
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(fn (BranchModel $model) => $this->map($model))
            ->all();
    }

    public function create(
        int $tenantId,
        string $name,
        string $code,
        ?string $address,
        string $status,
    ): Branch {
        $model = BranchModel::query()->create([
            'tenant_id' => $tenantId,
            'name' => $name,
            'code' => $code,
            'address' => $address,
            'status' => $status,
        ]);

        return $this->map($model);
    }

    public function update(
        int $id,
        string $name,
        string $code,
        ?string $address,
        string $status,
    ): Branch {
        $model = BranchModel::query()->findOrFail($id);

        $model->update([
            'name' => $name,
            'code' => $code,
            'address' => $address,
            'status' => $status,
        ]);

        return $this->map($model->fresh());
    }

    public function codeExistsForTenant(int $tenantId, string $code, ?int $exceptBranchId = null): bool
    {
        $query = BranchModel::query()
            ->where('tenant_id', $tenantId)
            ->where('code', $code);

        if ($exceptBranchId !== null) {
            $query->where('id', '!=', $exceptBranchId);
        }

        return $query->exists();
    }

    private function map(BranchModel $model): Branch
    {
        return new Branch(
            id: (int) $model->id,
            tenantId: (int) $model->tenant_id,
            name: $model->name,
            code: $model->code,
            address: $model->address,
            status: $model->status,
        );
    }
}
