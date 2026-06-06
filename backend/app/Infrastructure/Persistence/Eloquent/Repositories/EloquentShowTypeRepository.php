<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\ShowType\Repositories\ShowTypeRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\ShowTypeModel;

final class EloquentShowTypeRepository implements ShowTypeRepositoryInterface
{
    public function listForBranch(int $tenantId, ?int $branchId): array
    {
        return ShowTypeModel::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where(function ($query) use ($branchId) {
                $query->whereNull('branch_id');
                if ($branchId !== null) {
                    $query->orWhere('branch_id', $branchId);
                }
            })
            ->orderBy('name')
            ->get()
            ->map(fn (ShowTypeModel $m) => $this->map($m))
            ->all();
    }

    public function nameExists(int $tenantId, string $name, ?int $exceptId = null): bool
    {
        $query = ShowTypeModel::query()
            ->where('tenant_id', $tenantId)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($name))]);

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }

    public function create(
        int $tenantId,
        ?int $branchId,
        string $name,
        ?string $suggestedPrice,
        string $status,
    ): array {
        $model = ShowTypeModel::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'name' => trim($name),
            'suggested_price' => $suggestedPrice,
            'status' => $status,
        ]);

        return $this->map($model);
    }

    public function update(
        int $id,
        int $tenantId,
        string $name,
        ?string $suggestedPrice,
        string $status,
    ): array {
        $model = ShowTypeModel::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($id);

        $model->update([
            'name' => trim($name),
            'suggested_price' => $suggestedPrice,
            'status' => $status,
        ]);

        return $this->map($model->fresh());
    }

    public function findById(int $id, int $tenantId): ?array
    {
        $model = ShowTypeModel::query()
            ->where('tenant_id', $tenantId)
            ->find($id);

        return $model ? $this->map($model) : null;
    }

    private function map(ShowTypeModel $model): array
    {
        return [
            'id' => (int) $model->id,
            'tenant_id' => (int) $model->tenant_id,
            'branch_id' => $model->branch_id !== null ? (int) $model->branch_id : null,
            'name' => $model->name,
            'suggested_price' => $model->suggested_price !== null ? (string) $model->suggested_price : null,
            'status' => $model->status,
        ];
    }
}
