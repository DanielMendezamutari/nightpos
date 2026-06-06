<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Product\Entities\ProductCategory;
use App\Domain\Product\Repositories\ProductCategoryRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\ProductCategoryModel;

final class EloquentProductCategoryRepository implements ProductCategoryRepositoryInterface
{
    public function findById(int $id, int $tenantId): ?ProductCategory
    {
        $model = ProductCategoryModel::query()
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();

        return $model ? $this->map($model) : null;
    }

    public function listForTenant(int $tenantId, ?int $branchId): array
    {
        $query = ProductCategoryModel::query()->where('tenant_id', $tenantId);

        if ($branchId !== null) {
            $query->where(function ($builder) use ($branchId) {
                $builder->whereNull('branch_id')
                    ->orWhere('branch_id', $branchId);
            });
        }

        return $query->orderBy('name')
            ->get()
            ->map(fn (ProductCategoryModel $model) => $this->map($model))
            ->all();
    }

    public function create(
        int $tenantId,
        ?int $branchId,
        string $name,
        string $type,
        string $status,
    ): ProductCategory {
        $model = ProductCategoryModel::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'name' => $name,
            'type' => $type,
            'status' => $status,
        ]);

        return $this->map($model);
    }

    public function update(
        int $id,
        int $tenantId,
        string $name,
        string $type,
        string $status,
    ): ProductCategory {
        $model = ProductCategoryModel::query()
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        $model->update([
            'name' => $name,
            'type' => $type,
            'status' => $status,
        ]);

        return $this->map($model->fresh());
    }

    private function map(ProductCategoryModel $model): ProductCategory
    {
        return new ProductCategory(
            id: (int) $model->id,
            tenantId: (int) $model->tenant_id,
            branchId: $model->branch_id !== null ? (int) $model->branch_id : null,
            name: $model->name,
            type: $model->type,
            status: $model->status,
        );
    }
}
