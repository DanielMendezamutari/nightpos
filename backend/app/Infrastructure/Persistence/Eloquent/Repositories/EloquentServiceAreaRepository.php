<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Settings\Repositories\ServiceAreaRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\ServiceAreaModel;

final class EloquentServiceAreaRepository implements ServiceAreaRepositoryInterface
{
    public function listForBranch(int $tenantId, int $branchId, bool $activeOnly = false): array
    {
        $query = ServiceAreaModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId);

        if ($activeOnly) {
            $query->where('status', 'active');
        }

        return $query->orderBy('name')->get()
            ->map(fn (ServiceAreaModel $m) => $this->map($m))
            ->all();
    }

    public function findById(int $id, int $tenantId, int $branchId): ?array
    {
        $model = ServiceAreaModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->find($id);

        return $model ? $this->map($model) : null;
    }

    public function create(
        int $tenantId,
        int $branchId,
        string $code,
        string $name,
        string $areaType,
        string $status,
    ): array {
        $model = ServiceAreaModel::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'code' => strtoupper(trim($code)),
            'name' => trim($name),
            'area_type' => strtoupper($areaType),
            'status' => $status,
        ]);

        return $this->map($model);
    }

    public function update(int $id, int $tenantId, string $name, string $areaType, string $status): array
    {
        $model = ServiceAreaModel::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($id);

        $model->update([
            'name' => trim($name),
            'area_type' => strtoupper($areaType),
            'status' => $status,
        ]);

        return $this->map($model->fresh());
    }

    public function codeExists(int $branchId, string $code, ?int $exceptId = null): bool
    {
        $query = ServiceAreaModel::query()
            ->where('branch_id', $branchId)
            ->where('code', strtoupper(trim($code)));

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }

    private function map(ServiceAreaModel $model): array
    {
        return [
            'id' => (int) $model->id,
            'tenant_id' => (int) $model->tenant_id,
            'branch_id' => (int) $model->branch_id,
            'code' => $model->code,
            'name' => $model->name,
            'area_type' => $model->area_type,
            'status' => $model->status,
        ];
    }
}
