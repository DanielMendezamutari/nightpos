<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Settings\Repositories\RoomTypeCatalogRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\RoomTypeCatalogModel;

final class EloquentRoomTypeCatalogRepository implements RoomTypeCatalogRepositoryInterface
{
    public function listForBranch(int $tenantId, int $branchId, bool $activeOnly = false): array
    {
        $query = RoomTypeCatalogModel::query()
            ->where('tenant_id', $tenantId)
            ->where(function ($q) use ($branchId) {
                $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
            });

        if ($activeOnly) {
            $query->where('status', 'active');
        }

        return $query->orderBy('name')->get()
            ->map(fn (RoomTypeCatalogModel $m) => $this->map($m))
            ->all();
    }

    public function findById(int $id, int $tenantId): ?array
    {
        $model = RoomTypeCatalogModel::query()
            ->where('tenant_id', $tenantId)
            ->find($id);

        return $model ? $this->map($model) : null;
    }

    public function findByCode(int $tenantId, string $code): ?array
    {
        $model = RoomTypeCatalogModel::query()
            ->where('tenant_id', $tenantId)
            ->where('code', strtoupper(trim($code)))
            ->where('status', 'active')
            ->first();

        return $model ? $this->map($model) : null;
    }

    public function create(
        int $tenantId,
        ?int $branchId,
        string $code,
        string $name,
        int $defaultDurationMinutes,
        string $suggestedPrice,
        string $status,
    ): array {
        $model = RoomTypeCatalogModel::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'code' => strtoupper(trim($code)),
            'name' => trim($name),
            'default_duration_minutes' => $defaultDurationMinutes,
            'suggested_price' => $suggestedPrice,
            'status' => $status,
        ]);

        return $this->map($model);
    }

    public function update(
        int $id,
        int $tenantId,
        string $name,
        int $defaultDurationMinutes,
        string $suggestedPrice,
        string $status,
    ): array {
        $model = RoomTypeCatalogModel::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($id);

        $model->update([
            'name' => trim($name),
            'default_duration_minutes' => $defaultDurationMinutes,
            'suggested_price' => $suggestedPrice,
            'status' => $status,
        ]);

        return $this->map($model->fresh());
    }

    public function codeExists(int $tenantId, string $code, ?int $exceptId = null): bool
    {
        $query = RoomTypeCatalogModel::query()
            ->where('tenant_id', $tenantId)
            ->where('code', strtoupper(trim($code)));

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }

    private function map(RoomTypeCatalogModel $model): array
    {
        return [
            'id' => (int) $model->id,
            'tenant_id' => (int) $model->tenant_id,
            'branch_id' => $model->branch_id !== null ? (int) $model->branch_id : null,
            'code' => $model->code,
            'name' => $model->name,
            'default_duration_minutes' => (int) $model->default_duration_minutes,
            'suggested_price' => (string) $model->suggested_price,
            'status' => $model->status,
        ];
    }
}
